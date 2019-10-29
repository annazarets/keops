"""Delete comments

Revision ID: 4674a545b1b0
Revises: ee294d2ae5e3
Create Date: 2019-09-18 13:29:34.952726

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '4674a545b1b0'
down_revision = 'ee294d2ae5e3'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_column('sentences_tasks', 'comments', schema='keopsdb')
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.add_column('sentences_tasks', sa.Column('comments', sa.VARCHAR(length=1000), autoincrement=False, nullable=True), schema='keopsdb')
    # ### end Alembic commands ###
